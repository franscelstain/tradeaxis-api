<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC131WeeklySwingWatchlistProductionLiveRuntimeActivationApprovalReviewService;
use Illuminate\Console\Command;

class RunBacktestC131WeeklySwingWatchlistProductionLiveRuntimeActivationApprovalReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review
        {--c130-artifact=storage/app/watchlist/backtest/c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review.json}
        {--expected-c130-hash=b4c4d48a672a953fee5fc5e79459817c34863775}
        {--expected-c130-file-sha1=B244D23169FA9B01B473382398BE7C847A0C2794}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c131-weekly-swing-watchlist-production-live-runtime-activation-approval-review.json}
        {--operator-approved}
        {--production-live-runtime-activation-approval-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C131 weekly swing watchlist production/live runtime activation approval review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC131WeeklySwingWatchlistProductionLiveRuntimeActivationApprovalReviewService $service;

    public function __construct(?WatchlistBacktestC131WeeklySwingWatchlistProductionLiveRuntimeActivationApprovalReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC131WeeklySwingWatchlistProductionLiveRuntimeActivationApprovalReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C131 weekly swing watchlist production/live runtime activation approval review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c130-artifact'),
            (string) $this->option('expected-c130-hash'),
            (string) $this->option('expected-c130-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'production_live_runtime_activation_approval_confirmed' => (bool) $this->option('production-live-runtime-activation-approval-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_approval_review_pass',
            'production_live_runtime_activation_approval_review_pass',
            'production_live_runtime_activation_approval_granted',
            'ready_for_production_live_runtime_activation_execution_review',
            'production_live_runtime_activation_execution_review_allowed_next',
            'production_live_runtime_activation_approval_manifest_created',
            'c130_lock_valid', 'c130_activation_readiness_valid', 'c130_convert_from_json_pass',
            'c129_final_closure_valid', 'c129_audit_archive_terminal',
            'c130_activation_readiness_review_only', 'c130_not_live_runtime_activation_execution',
            'primary_candidate_ready_for_production_live_runtime_activation_execution_review',
            'backup_candidate_ready_for_production_live_runtime_activation_execution_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_execution_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c130_hash', 'actual_c130_hash', 'c130_hash_match',
            'expected_c130_file_sha1', 'actual_c130_file_sha1', 'c130_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_execution_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C131_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_APPROVAL_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C131 weekly swing watchlist production/live runtime activation approval review completed');
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
