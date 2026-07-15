<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review
        {--c148-artifact=storage/app/watchlist/backtest/c148-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json}
        {--expected-c148-hash=d5420447a0b5994791e51f65318dcc46c75ec156}
        {--expected-c148-file-sha1=9EF227B2B7944B2406D15235DC6C84264466B81F}
        {--approval-reference=}
        {--operator-decision=}
        {--decision-reason=}
        {--output=storage/app/watchlist/backtest/c149-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json}
        {--operator-approved}
        {--operator-decision-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C149 weekly swing watchlist production/live runtime activation operator GO/NO-GO/HOLD review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService $service;

    public function __construct(?WatchlistBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC149WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C149 weekly swing watchlist production/live runtime activation operator GO/NO-GO/HOLD review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c148-artifact'),
            (string) $this->option('expected-c148-hash'),
            (string) $this->option('expected-c148-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'operator_decision_confirmed' => (bool) $this->option('operator-decision-confirmed'),
                'operator_decision' => (string) $this->option('operator-decision'),
                'decision_reason' => (string) $this->option('decision-reason'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass',
            'production_live_runtime_activation_operator_go_no_go_review_pass',
            'operator_decision_recorded', 'operator_decision', 'operator_go_decision',
            'operator_no_go_decision', 'operator_hold_decision', 'operator_decision_confirmed',
            'operator_decision_reason',
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_final_execution',
            'ready_for_production_live_runtime_activation_final_execution',
            'production_live_runtime_activation_operator_go_no_go_manifest_created',
            'production_live_runtime_activation_final_execution_allowed_next',
            'production_live_runtime_activation_stopped_no_go',
            'production_live_runtime_activation_deferred_hold',
            'production_live_runtime_activation_executed',
            'c148_lock_valid', 'c148_activation_observation_result_review_valid', 'c148_convert_from_json_pass',
            'c147_activation_observation_review_valid', 'c146_activation_execution_review_valid',
            'c145_activation_authorization_valid', 'c144_pre_activation_boundary_valid',
            'c143_go_decision_finalization_valid', 'c142_activation_operator_go_no_go_valid',
            'c141_activation_observation_result_review_valid', 'c140_activation_observation_review_valid',
            'c139_activation_execution_review_valid', 'c138_activation_authorization_valid',
            'c137_pre_activation_boundary_valid', 'c136_go_decision_finalization_valid',
            'c135_activation_operator_go_no_go_valid', 'c134_activation_observation_result_review_valid',
            'c133_activation_observation_review_valid', 'c132_activation_execution_review_valid',
            'c131_activation_approval_valid', 'c130_activation_readiness_valid',
            'c129_final_closure_valid',
            'activation_authorized', 'primary_candidate_activation_authorized',
            'backup_candidate_activation_authorized', 'comparator_candidate_activation_authorized',
            'c148_observation_result_review_only', 'c148_not_live_runtime_state_change',
            'c149_operator_go_no_go_review_only', 'c149_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_final_execution',
            'backup_candidate_ready_for_production_live_runtime_activation_final_execution',
            'comparator_candidate_ready_for_production_live_runtime_activation_final_execution',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c148_hash', 'actual_c148_hash', 'c148_hash_match',
            'expected_c148_file_sha1', 'actual_c148_file_sha1', 'c148_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_concrete_activation_step_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if ($this->isCompletedDecisionStatus((string) ($result['status'] ?? ''))) {
            if ((bool) $this->option('progress')) {
                $this->line('C149 weekly swing watchlist production/live runtime activation operator GO/NO-GO/HOLD review completed');
            }

            return 0;
        }

        if (($result['message'] ?? null) !== null) {
            $this->error((string) $result['message']);
        }

        return 1;
    }

    private function isCompletedDecisionStatus(string $status): bool
    {
        foreach ([
            'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED_GO',
            'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_NO_GO',
            'C149_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_COMPLETED_HOLD',
        ] as $prefix) {
            if (strpos($status, $prefix) === 0) {
                return true;
            }
        }

        return false;
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
