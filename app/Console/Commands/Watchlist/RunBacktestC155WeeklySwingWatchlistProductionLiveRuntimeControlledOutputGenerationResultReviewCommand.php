<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC155WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationResultReviewService;
use Illuminate\Console\Command;

class RunBacktestC155WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationResultReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review
        {--c154-artifact=storage/app/watchlist/backtest/c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution.json}
        {--expected-c154-hash=cd321cbbbbc1fa3902da5928a61741e80c8bd437}
        {--expected-c154-file-sha1=82C8C90E04A7B7C5208BC37E40CAC8B02673CACB}
        {--controlled-output=storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json}
        {--expected-controlled-output-hash=a1ca6b200993e4c70c7dccfa62ace43ffb2f7c4e}
        {--expected-controlled-output-file-sha1=AFCA465B7567AFA37034388B257F5F5808B17E5F}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c155-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-result-review.json}
        {--operator-approved}
        {--result-review-confirmed}
        {--no-publication-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C155 weekly swing watchlist production/live runtime controlled output-generation result review.';

    private WatchlistBacktestC155WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationResultReviewService $service;

    public function __construct(?WatchlistBacktestC155WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationResultReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC155WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationResultReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C155 weekly swing watchlist production/live runtime controlled output-generation result review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c154-artifact'),
            (string) $this->option('expected-c154-hash'),
            (string) $this->option('expected-c154-file-sha1'),
            (string) $this->option('controlled-output'),
            (string) $this->option('expected-controlled-output-hash'),
            (string) $this->option('expected-controlled-output-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'result_review_confirmed' => (bool) $this->option('result-review-confirmed'),
                'no_publication_confirmed' => (bool) $this->option('no-publication-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_output_path', 'controlled_output_hash', 'controlled_output_file_sha1',
            'controlled_output_record_count',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_result_review_pass',
            'production_live_runtime_controlled_output_generation_result_review_pass',
            'weekly_swing_watchlist_controlled_output_generation_result_reviewed',
            'weekly_swing_watchlist_controlled_output_generation_result_review_manifest_created',
            'ready_for_weekly_swing_watchlist_controlled_output_generation_operator_go_no_go_review',
            'production_live_runtime_controlled_output_generation_operator_go_no_go_review_allowed_next',
            'weekly_swing_watchlist_controlled_output_generation_executed',
            'weekly_swing_watchlist_controlled_output_generated',
            'weekly_swing_watchlist_controlled_output_artifact_created',
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
            'c154_lock_valid', 'c154_controlled_output_generation_execution_valid', 'c154_convert_from_json_pass',
            'controlled_output_lock_valid', 'controlled_output_convert_from_json_pass', 'controlled_output_integrity_valid',
            'primary_candidate_controlled_output_result_reviewed',
            'backup_candidate_controlled_output_result_reviewed',
            'comparator_candidate_controlled_output_result_reviewed',
            'a01_remains_comparator_only',
            'c155_controlled_output_generation_result_review_only',
            'c155_not_publication', 'c155_not_unrestricted_publication', 'c155_not_plan_confirm_mutation',
            'operator_approved', 'approval_reference',
            'result_review_confirmed', 'no_publication_confirmed', 'plan_confirm_unchanged_confirmed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c154_hash', 'actual_c154_hash', 'c154_hash_match',
            'expected_c154_file_sha1', 'actual_c154_file_sha1', 'c154_file_sha1_match',
            'expected_controlled_output_hash', 'actual_controlled_output_hash', 'controlled_output_hash_match',
            'expected_controlled_output_file_sha1', 'actual_controlled_output_file_sha1', 'controlled_output_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C155_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_RESULT_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C155 weekly swing watchlist production/live runtime controlled output-generation result review completed');
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
