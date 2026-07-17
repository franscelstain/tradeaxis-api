<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmExecutionService;
use Illuminate\Console\Command;

class RunBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmExecutionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-execution
        {--c160-boundary-artifact=storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-boundary-review.json}
        {--expected-c160-boundary-hash=b9ca7ca795c2d3a75ad2910263d5a7b3c249bab9}
        {--expected-c160-boundary-file-sha1=D5C708775E5E6DEC644ACD54DEBBEDD370329004}
        {--controlled-publication=storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json}
        {--expected-controlled-publication-hash=df064c7290ff4c3bfd0c7a8412d39299049c01d5}
        {--expected-controlled-publication-file-sha1=D87AB8CD1564BE8B266B8A68011470272D49EE60}
        {--controlled-plan-confirm=storage/app/watchlist/output/c160-weekly-swing-watchlist-controlled-plan-confirm.json}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c160-weekly-swing-watchlist-production-live-runtime-plan-confirm-execution.json}
        {--operator-approved}
        {--plan-confirm-execution-confirmed}
        {--controlled-plan-confirm-only-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--no-live-plan-confirm-rollout-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C160 weekly swing watchlist production/live runtime PLAN/CONFIRM execution.';

    private WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmExecutionService $service;

    public function __construct(?WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmExecutionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC160WeeklySwingWatchlistProductionLiveRuntimePlanConfirmExecutionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C160 weekly swing watchlist production/live runtime PLAN/CONFIRM execution started');
        }

        $result = $this->service->execute(
            (string) $this->option('c160-boundary-artifact'),
            (string) $this->option('expected-c160-boundary-hash'),
            (string) $this->option('expected-c160-boundary-file-sha1'),
            (string) $this->option('controlled-publication'),
            (string) $this->option('expected-controlled-publication-hash'),
            (string) $this->option('expected-controlled-publication-file-sha1'),
            (string) $this->option('output'),
            (string) $this->option('controlled-plan-confirm'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'plan_confirm_execution_confirmed' => (bool) $this->option('plan-confirm-execution-confirmed'),
                'controlled_plan_confirm_only_confirmed' => (bool) $this->option('controlled-plan-confirm-only-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'no_live_plan_confirm_rollout_confirmed' => (bool) $this->option('no-live-plan-confirm-rollout-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_plan_confirm_path', 'controlled_plan_confirm_hash', 'controlled_plan_confirm_file_sha1', 'controlled_plan_confirm_record_count',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_executed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_allowed',
            'weekly_swing_watchlist_production_live_runtime_plan_confirm_execution_pass',
            'production_live_runtime_plan_confirm_execution_pass',
            'ready_for_weekly_swing_watchlist_plan_confirm_result_review',
            'production_live_runtime_plan_confirm_result_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_result_review_allowed_next',
            'weekly_swing_watchlist_plan_confirm_controlled_execution_executed',
            'weekly_swing_watchlist_plan_confirm_controlled_artifact_created',
            'weekly_swing_watchlist_plan_confirm_controlled_only',
            'weekly_swing_watchlist_controlled_output_publication_executed',
            'weekly_swing_watchlist_controlled_output_published',
            'weekly_swing_watchlist_controlled_publication_allowed',
            'weekly_swing_watchlist_official_output_generated',
            'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_publication_allowed',
            'weekly_swing_watchlist_unrestricted_publication_allowed',
            'plan_confirm_execution_confirmed', 'controlled_plan_confirm_only_confirmed',
            'plan_confirm_unchanged_confirmed', 'no_live_plan_confirm_rollout_confirmed',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'plan_confirm_runtime_reads_activated_catalog',
            'live_plan_confirm_rollout_allowed', 'live_plan_confirm_rollout_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'c160_boundary_lock_valid', 'c160_plan_confirm_boundary_valid', 'c160_boundary_convert_from_json_pass',
            'controlled_publication_lock_valid', 'controlled_publication_integrity_valid', 'controlled_publication_convert_from_json_pass',
            'operator_approved', 'approval_reference',
            'primary_candidate_plan_confirm_controlled_executed',
            'backup_candidate_plan_confirm_controlled_executed',
            'comparator_candidate_plan_confirm_controlled_executed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'c160_plan_confirm_execution_only', 'c160_controlled_plan_confirm_only',
            'c160_not_plan_confirm_mutation', 'c160_not_live_plan_confirm_rollout', 'c160_not_publication',
            'c160_topic_number_retained_for_result_review',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c160_boundary_hash', 'actual_c160_boundary_hash', 'c160_boundary_hash_match',
            'expected_c160_boundary_file_sha1', 'actual_c160_boundary_file_sha1', 'c160_boundary_file_sha1_match',
            'expected_controlled_publication_hash', 'actual_controlled_publication_hash', 'controlled_publication_hash_match',
            'expected_controlled_publication_file_sha1', 'actual_controlled_publication_file_sha1', 'controlled_publication_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C160_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_PLAN_CONFIRM_EXECUTION_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C160 weekly swing watchlist production/live runtime PLAN/CONFIRM execution completed');
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
