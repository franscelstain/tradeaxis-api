<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationExecutionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution
        {--c158-boundary-artifact=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-boundary-review.json}
        {--expected-c158-boundary-hash=f17826dd8eb388491be7ef94d18600647dbccc85}
        {--expected-c158-boundary-file-sha1=B61A0522835494811E3306ABDFE37639D5ED56C8}
        {--controlled-output=storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json}
        {--expected-controlled-output-hash=a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e}
        {--expected-controlled-output-file-sha1=AFCA465B7567AFA37034388B257F5F5808B17E5F}
        {--controlled-publication=storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution.json}
        {--operator-approved}
        {--controlled-publication-execution-confirmed}
        {--controlled-publication-only-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C158 weekly swing watchlist production/live runtime controlled output publication execution.';

    private WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationExecutionService $service;

    public function __construct(?WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationExecutionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationExecutionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C158 weekly swing watchlist production/live runtime controlled output publication execution started');
        }

        $result = $this->service->execute(
            (string) $this->option('c158-boundary-artifact'),
            (string) $this->option('expected-c158-boundary-hash'),
            (string) $this->option('expected-c158-boundary-file-sha1'),
            (string) $this->option('controlled-output'),
            (string) $this->option('expected-controlled-output-hash'),
            (string) $this->option('expected-controlled-output-file-sha1'),
            (string) $this->option('output'),
            (string) $this->option('controlled-publication'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'controlled_publication_execution_confirmed' => (bool) $this->option('controlled-publication-execution-confirmed'),
                'controlled_publication_only_confirmed' => (bool) $this->option('controlled-publication-only-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_publication_path', 'controlled_publication_hash', 'controlled_publication_file_sha1', 'controlled_publication_record_count',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_execution_pass',
            'production_live_runtime_controlled_output_publication_execution_pass',
            'ready_for_weekly_swing_watchlist_controlled_output_publication_result_review',
            'production_live_runtime_controlled_output_publication_result_review_allowed_next',
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
            'c158_boundary_lock_valid', 'c158_publication_boundary_valid', 'c158_boundary_convert_from_json_pass',
            'controlled_output_lock_valid', 'controlled_output_integrity_valid', 'controlled_output_convert_from_json_pass',
            'operator_approved', 'approval_reference',
            'controlled_publication_execution_confirmed', 'controlled_publication_only_confirmed', 'plan_confirm_unchanged_confirmed',
            'primary_candidate_controlled_published', 'backup_candidate_controlled_published', 'comparator_candidate_controlled_published',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code', 'a01_remains_comparator_only',
            'c158_controlled_output_publication_execution_only', 'c158_controlled_publication_only',
            'c158_not_free_publication', 'c158_not_unrestricted_publication', 'c158_not_plan_confirm_mutation',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c158_boundary_hash', 'actual_c158_boundary_hash', 'c158_boundary_hash_match',
            'expected_c158_boundary_file_sha1', 'actual_c158_boundary_file_sha1', 'c158_boundary_file_sha1_match',
            'expected_controlled_output_hash', 'actual_controlled_output_hash', 'controlled_output_hash_match',
            'expected_controlled_output_file_sha1', 'actual_controlled_output_file_sha1', 'controlled_output_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_EXECUTION_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C158 weekly swing watchlist production/live runtime controlled output publication execution completed');
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
