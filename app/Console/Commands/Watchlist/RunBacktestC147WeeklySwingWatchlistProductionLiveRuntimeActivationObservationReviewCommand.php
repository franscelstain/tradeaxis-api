<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC147WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService;
use Illuminate\Console\Command;

class RunBacktestC147WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review
        {--c146-artifact=storage/app/watchlist/backtest/c146-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json}
        {--expected-c146-hash=ff6549aa99b2488ce52184dd818190b124e480ce}
        {--expected-c146-file-sha1=1291AADFB2CC7691D868AD86604731C2F6F5D9F2}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c147-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C147 weekly swing watchlist production/live runtime activation observation review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC147WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService $service;

    public function __construct(?WatchlistBacktestC147WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC147WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C147 weekly swing watchlist production/live runtime activation observation review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c146-artifact'),
            (string) $this->option('expected-c146-hash'),
            (string) $this->option('expected-c146-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_observation_review_pass',
            'production_live_runtime_activation_observation_review_pass',
            'ready_for_production_live_runtime_activation_observation_result_review',
            'production_live_runtime_activation_observation_result_review_allowed_next',
            'production_live_runtime_activation_observation_review_manifest_created',
            'production_live_runtime_activation_executed',
            'c146_lock_valid', 'c146_activation_execution_review_valid', 'c146_convert_from_json_pass',
            'c145_lock_valid', 'c145_activation_authorization_valid', 'c145_convert_from_json_pass',
            'c144_lock_valid', 'c144_pre_activation_boundary_valid', 'c144_convert_from_json_pass',
            'c143_lock_valid', 'c143_go_decision_finalization_valid', 'c143_convert_from_json_pass',
            'c142_lock_valid', 'c142_activation_operator_go_no_go_valid', 'c142_convert_from_json_pass',
            'c141_activation_observation_result_review_valid', 'c140_activation_observation_review_valid',
            'c139_activation_execution_review_valid', 'c138_activation_authorization_valid', 'c137_pre_activation_boundary_valid',
            'c136_go_decision_finalization_valid', 'c135_activation_operator_go_no_go_valid',
            'c134_activation_observation_result_review_valid', 'c133_activation_observation_review_valid',
            'c132_activation_execution_review_valid', 'c131_activation_approval_valid',
            'c130_activation_readiness_valid', 'c129_final_closure_valid',
            'activation_authorized', 'primary_candidate_activation_authorized',
            'backup_candidate_activation_authorized', 'comparator_candidate_activation_authorized',
            'c138_activation_authorization_review_only', 'c138_not_activation_execution',
            'c138_not_live_runtime_state_change',
            'c139_execution_review_only', 'c139_not_live_runtime_state_change',
            'c140_observation_review_only', 'c140_not_live_runtime_state_change',
            'c141_observation_result_review_only', 'c141_not_live_runtime_state_change',
            'c142_operator_go_no_go_review_only', 'c142_not_live_runtime_state_change',
            'c143_go_decision_finalization_review_only', 'c144_pre_activation_boundary_review_only',
            'c144_not_activation_authorization',
            'c145_activation_authorization_review_only', 'c145_not_activation_execution',
            'c145_not_live_runtime_state_change',
            'c146_execution_review_only', 'c146_not_live_runtime_state_change',
            'c147_observation_review_only', 'c147_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_observation_result_review',
            'backup_candidate_ready_for_production_live_runtime_activation_observation_result_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_observation_result_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c146_hash', 'actual_c146_hash', 'c146_hash_match',
            'expected_c146_file_sha1', 'actual_c146_file_sha1', 'c146_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_observation_result_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C147_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C147 weekly swing watchlist production/live runtime activation observation review completed');
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
