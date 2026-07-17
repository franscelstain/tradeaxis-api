<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review
        {--c158-execution-artifact=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-execution.json}
        {--expected-c158-execution-hash=fec3b624eb3e912b1302165b1def8fe0a4669a87}
        {--expected-c158-execution-file-sha1=242830E193C2D54A4C7A233A68D04F90412AEE7D}
        {--controlled-publication=storage/app/watchlist/output/c158-weekly-swing-watchlist-controlled-publication.json}
        {--expected-controlled-publication-hash=df064c7290ff4c3bfd0c7a8412d39299049c01d5}
        {--expected-controlled-publication-file-sha1=D87AB8CD1564BE8B266B8A68011470272D49EE60}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c158-weekly-swing-watchlist-production-live-runtime-controlled-output-publication-result-review.json}
        {--operator-approved}
        {--result-review-confirmed}
        {--controlled-publication-result-confirmed}
        {--controlled-publication-only-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C158 weekly swing watchlist production/live runtime controlled output publication result review.';

    private WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationResultReviewService $service;

    public function __construct(?WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC158WeeklySwingWatchlistProductionLiveRuntimeControlledOutputPublicationResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C158 weekly swing watchlist production/live runtime controlled output publication result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c158-execution-artifact'),
            (string) $this->option('expected-c158-execution-hash'),
            (string) $this->option('expected-c158-execution-file-sha1'),
            (string) $this->option('controlled-publication'),
            (string) $this->option('expected-controlled-publication-hash'),
            (string) $this->option('expected-controlled-publication-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'result_review_confirmed' => (bool) $this->option('result-review-confirmed'),
                'controlled_publication_result_confirmed' => (bool) $this->option('controlled-publication-result-confirmed'),
                'controlled_publication_only_confirmed' => (bool) $this->option('controlled-publication-only-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'topic_code', 'topic_stage', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_publication_path', 'controlled_publication_hash', 'controlled_publication_file_sha1',
            'controlled_publication_record_count',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_publication_result_review_pass',
            'production_live_runtime_controlled_output_publication_result_review_pass',
            'weekly_swing_watchlist_controlled_output_publication_result_reviewed',
            'weekly_swing_watchlist_controlled_output_publication_result_review_manifest_created',
            'ready_for_weekly_swing_watchlist_controlled_output_publication_operator_go_no_go_review',
            'production_live_runtime_controlled_output_publication_operator_go_no_go_review_allowed_next',
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
            'c158_execution_lock_valid', 'c158_publication_execution_valid', 'c158_execution_convert_from_json_pass',
            'controlled_publication_lock_valid', 'controlled_publication_convert_from_json_pass', 'controlled_publication_integrity_valid',
            'primary_candidate_controlled_publication_result_reviewed',
            'backup_candidate_controlled_publication_result_reviewed',
            'comparator_candidate_controlled_publication_result_reviewed',
            'primary_candidate_code', 'backup_candidate_code', 'comparator_candidate_code',
            'a01_remains_comparator_only',
            'c158_controlled_output_publication_result_review_only',
            'c158_controlled_publication_only',
            'c158_not_free_publication', 'c158_not_unrestricted_publication', 'c158_not_plan_confirm_mutation',
            'operator_approved', 'approval_reference',
            'result_review_confirmed', 'controlled_publication_result_confirmed', 'controlled_publication_only_confirmed', 'plan_confirm_unchanged_confirmed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c158_execution_hash', 'actual_c158_execution_hash', 'c158_execution_hash_match',
            'expected_c158_execution_file_sha1', 'actual_c158_execution_file_sha1', 'c158_execution_file_sha1_match',
            'expected_controlled_publication_hash', 'actual_controlled_publication_hash', 'controlled_publication_hash_match',
            'expected_controlled_publication_file_sha1', 'actual_controlled_publication_file_sha1', 'controlled_publication_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C158_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_PUBLICATION_RESULT_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C158 weekly swing watchlist production/live runtime controlled output publication result review completed');
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
