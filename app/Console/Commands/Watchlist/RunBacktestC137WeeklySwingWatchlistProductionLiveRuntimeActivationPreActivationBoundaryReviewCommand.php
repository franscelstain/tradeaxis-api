<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC137WeeklySwingWatchlistProductionLiveRuntimeActivationPreActivationBoundaryReviewService;
use Illuminate\Console\Command;

class RunBacktestC137WeeklySwingWatchlistProductionLiveRuntimeActivationPreActivationBoundaryReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review
        {--c136-artifact=storage/app/watchlist/backtest/c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json}
        {--expected-c136-hash=38eee6c7216fd94421c65be129ba50c4a93fd1d1}
        {--expected-c136-file-sha1=1B395D673F04AE8A7FD62527259DA2CFBA8244AF}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c137-weekly-swing-watchlist-production-live-runtime-activation-pre-activation-boundary-review.json}
        {--operator-approved}
        {--pre-activation-boundary-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C137 weekly swing watchlist production/live runtime activation pre-activation boundary review without activation authorization, runtime bridge activation, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC137WeeklySwingWatchlistProductionLiveRuntimeActivationPreActivationBoundaryReviewService $service;

    public function __construct(?WatchlistBacktestC137WeeklySwingWatchlistProductionLiveRuntimeActivationPreActivationBoundaryReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC137WeeklySwingWatchlistProductionLiveRuntimeActivationPreActivationBoundaryReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C137 weekly swing watchlist production/live runtime activation pre-activation boundary review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c136-artifact'),
            (string) $this->option('expected-c136-hash'),
            (string) $this->option('expected-c136-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'pre_activation_boundary_confirmed' => (bool) $this->option('pre-activation-boundary-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_pre_activation_boundary_review_pass',
            'production_live_runtime_activation_pre_activation_boundary_review_pass',
            'pre_activation_boundary_confirmed', 'pre_activation_boundary_cleared',
            'primary_candidate_boundary_cleared', 'backup_candidate_boundary_cleared',
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_authorization_review',
            'ready_for_production_live_runtime_activation_authorization_review',
            'production_live_runtime_activation_pre_activation_boundary_manifest_created',
            'production_live_runtime_activation_authorization_review_allowed_next',
            'activation_authorized', 'production_live_runtime_activation_executed',
            'c136_lock_valid', 'c136_go_decision_finalization_valid', 'c136_convert_from_json_pass',
            'c135_activation_operator_go_no_go_valid', 'c134_activation_observation_result_review_valid',
            'c133_activation_observation_review_valid', 'c132_activation_execution_review_valid',
            'c131_activation_approval_valid', 'c130_activation_readiness_valid', 'c129_final_closure_valid',
            'c136_go_decision_finalization_review_only', 'c137_pre_activation_boundary_review_only',
            'c137_not_activation_authorization', 'c137_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_authorization_review',
            'backup_candidate_ready_for_production_live_runtime_activation_authorization_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_authorization_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c136_hash', 'actual_c136_hash', 'c136_hash_match',
            'expected_c136_file_sha1', 'actual_c136_file_sha1', 'c136_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_activation_authorization_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C137_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_PRE_ACTIVATION_BOUNDARY_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C137 weekly swing watchlist production/live runtime activation pre-activation boundary review completed');
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
