<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC154WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationExecutionService;
use Illuminate\Console\Command;

class RunBacktestC154WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationExecutionCommand extends Command
{
    protected $signature = 'watchlist:backtest-c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution
        {--c153-artifact=storage/app/watchlist/backtest/c153-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-boundary-review.json}
        {--expected-c153-hash=51bdfbcbb34ce49a185122f0df932451fd914a78}
        {--expected-c153-file-sha1=9B8A640C6C7C9DD1947AB4C69706C76F44793B43}
        {--controlled-output=storage/app/watchlist/output/c154-weekly-swing-watchlist-controlled-output.json}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c154-weekly-swing-watchlist-production-live-runtime-controlled-output-generation-execution.json}
        {--operator-approved}
        {--controlled-output-confirmed}
        {--no-publication-confirmed}
        {--plan-confirm-unchanged-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C154 weekly swing watchlist production/live runtime controlled output-generation execution.';

    private WatchlistBacktestC154WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationExecutionService $service;

    public function __construct(?WatchlistBacktestC154WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationExecutionService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC154WeeklySwingWatchlistProductionLiveRuntimeControlledOutputGenerationExecutionService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C154 weekly swing watchlist production/live runtime controlled output-generation execution started');
        }

        $result = $this->service->execute(
            (string) $this->option('c153-artifact'),
            (string) $this->option('expected-c153-hash'),
            (string) $this->option('expected-c153-file-sha1'),
            (string) $this->option('output'),
            (string) $this->option('controlled-output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'controlled_output_confirmed' => (bool) $this->option('controlled-output-confirmed'),
                'no_publication_confirmed' => (bool) $this->option('no-publication-confirmed'),
                'plan_confirm_unchanged_confirmed' => (bool) $this->option('plan-confirm-unchanged-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'controlled_output_path', 'controlled_output_hash', 'controlled_output_file_sha1',
            'controlled_output_record_count',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_executed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_allowed',
            'weekly_swing_watchlist_production_live_runtime_controlled_output_generation_execution_pass',
            'production_live_runtime_controlled_output_generation_execution_pass',
            'ready_for_weekly_swing_watchlist_controlled_output_generation_result_review',
            'production_live_runtime_controlled_output_generation_result_review_allowed_next',
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
            'production_live_runtime_activation_executed',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_executed',
            'runtime_bridge_active', 'weekly_swing_watchlist_runtime_active',
            'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_live_recommendation_generation_allowed',
            'c153_lock_valid', 'c153_controlled_output_generation_boundary_valid', 'c153_convert_from_json_pass',
            'c152_lock_valid', 'c152_controlled_output_generation_boundary_ready',
            'primary_candidate_live_runtime_active',
            'backup_candidate_live_runtime_standby_active',
            'comparator_candidate_live_runtime_active',
            'a01_remains_comparator_only',
            'c154_controlled_output_generation_execution_only',
            'c154_not_publication', 'c154_not_unrestricted_publication', 'c154_not_plan_confirm_mutation',
            'operator_approved', 'approval_reference',
            'controlled_output_confirmed', 'no_publication_confirmed', 'plan_confirm_unchanged_confirmed',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'expected_c153_hash', 'actual_c153_hash', 'c153_hash_match',
            'expected_c153_file_sha1', 'actual_c153_file_sha1', 'c153_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C154_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_CONTROLLED_OUTPUT_GENERATION_EXECUTION_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C154 weekly swing watchlist production/live runtime controlled output-generation execution completed');
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
