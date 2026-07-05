<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC130WeeklySwingWatchlistProductionLiveRuntimeActivationReadinessReviewService;
use Illuminate\Console\Command;

class RunBacktestC130WeeklySwingWatchlistProductionLiveRuntimeActivationReadinessReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review
        {--c129-artifact=storage/app/watchlist/backtest/c129-weekly-swing-watchlist-controlled-runtime-wiring-handoff-audit-archive-final-closure-review.json}
        {--expected-c129-hash=39b7a16acf266f9b8853d275ff8dff3ef582f716}
        {--expected-c129-file-sha1=BA9AE12F4111AED9DC973BF1EA1BAE9181844E9E}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c130-weekly-swing-watchlist-production-live-runtime-activation-readiness-review.json}
        {--operator-approved}
        {--production-live-runtime-activation-readiness-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C130 weekly swing watchlist production/live runtime activation readiness review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC130WeeklySwingWatchlistProductionLiveRuntimeActivationReadinessReviewService $service;

    public function __construct(?WatchlistBacktestC130WeeklySwingWatchlistProductionLiveRuntimeActivationReadinessReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC130WeeklySwingWatchlistProductionLiveRuntimeActivationReadinessReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C130 weekly swing watchlist production/live runtime activation readiness review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c129-artifact'),
            (string) $this->option('expected-c129-hash'),
            (string) $this->option('expected-c129-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
                'production_live_runtime_activation_readiness_confirmed' => (bool) $this->option('production-live-runtime-activation-readiness-confirmed'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_readiness_review_pass',
            'production_live_runtime_activation_readiness_review_pass',
            'ready_for_production_live_runtime_activation_approval_review',
            'production_live_runtime_activation_readiness_manifest_created',
            'c129_final_closure_valid', 'c129_handoff_audit_archive_final_closed', 'c129_audit_archive_terminal',
            'c130_is_new_production_live_activation_phase', 'c130_not_handoff_audit_archive_continuation',
            'primary_candidate_ready_for_production_live_runtime_activation_approval_review',
            'backup_candidate_ready_for_production_live_runtime_activation_approval_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_approval_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c129_hash', 'actual_c129_hash', 'c129_hash_match',
            'expected_c129_file_sha1', 'actual_c129_file_sha1', 'c129_file_sha1_match', 'c129_convert_from_json_pass',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_readiness_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C130_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_READINESS_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C130 weekly swing watchlist production/live runtime activation readiness review completed');
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
