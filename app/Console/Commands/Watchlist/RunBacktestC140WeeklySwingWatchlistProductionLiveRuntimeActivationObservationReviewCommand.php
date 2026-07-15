<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC140WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService;
use Illuminate\Console\Command;

class RunBacktestC140WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review
        {--c139-artifact=storage/app/watchlist/backtest/c139-weekly-swing-watchlist-production-live-runtime-activation-execution-review.json}
        {--expected-c139-hash=2b2e648433b2bf1e502246d879e7c5e5d943fba7}
        {--expected-c139-file-sha1=EDE1BC52EFDCF750304E31BB04677FD63912D296}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c140-weekly-swing-watchlist-production-live-runtime-activation-observation-review.json}
        {--operator-approved}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C140 weekly swing watchlist production/live runtime activation observation review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC140WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService $service;

    public function __construct(?WatchlistBacktestC140WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC140WeeklySwingWatchlistProductionLiveRuntimeActivationObservationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C140 weekly swing watchlist production/live runtime activation observation review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c139-artifact'),
            (string) $this->option('expected-c139-hash'),
            (string) $this->option('expected-c139-file-sha1'),
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
            'c139_lock_valid', 'c139_activation_execution_review_valid', 'c139_convert_from_json_pass',
            'c138_activation_authorization_valid', 'c137_pre_activation_boundary_valid',
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
            'expected_c139_hash', 'actual_c139_hash', 'c139_hash_match',
            'expected_c139_file_sha1', 'actual_c139_file_sha1', 'c139_file_sha1_match',
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

        if (strpos((string) ($result['status'] ?? ''), 'C140_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OBSERVATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C140 weekly swing watchlist production/live runtime activation observation review completed');
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
