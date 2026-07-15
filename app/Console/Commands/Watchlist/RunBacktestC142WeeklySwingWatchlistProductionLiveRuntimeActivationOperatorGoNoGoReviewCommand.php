<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC142WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService;
use Illuminate\Console\Command;

class RunBacktestC142WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review
        {--c141-artifact=storage/app/watchlist/backtest/c141-weekly-swing-watchlist-production-live-runtime-activation-observation-result-review.json}
        {--expected-c141-hash=ea7c4be969c2faf9e4990a135503829b8f6d6518}
        {--expected-c141-file-sha1=D9102B54D8719B40266AC8D4E9A0DF5B5BA5EB74}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c142-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json}
        {--operator-approved}
        {--operator-go-decision-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C142 weekly swing watchlist production/live runtime activation operator go/no-go review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC142WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService $service;

    public function __construct(?WatchlistBacktestC142WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC142WeeklySwingWatchlistProductionLiveRuntimeActivationOperatorGoNoGoReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C142 weekly swing watchlist production/live runtime activation operator go/no-go review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c141-artifact'),
            (string) $this->option('expected-c141-hash'),
            (string) $this->option('expected-c141-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'operator_go_decision_confirmed' => (bool) $this->option('operator-go-decision-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_operator_go_no_go_review_pass',
            'production_live_runtime_activation_operator_go_no_go_review_pass',
            'operator_go_decision', 'operator_go_decision_confirmed',
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'production_live_runtime_activation_operator_go_no_go_manifest_created',
            'production_live_runtime_activation_go_decision_finalization_review_allowed_next',
            'production_live_runtime_activation_executed',
            'c141_lock_valid', 'c141_activation_observation_result_review_valid', 'c141_convert_from_json_pass',
            'c140_activation_observation_review_valid', 'c139_activation_execution_review_valid',
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
            'c141_observation_result_review_only', 'c141_not_live_runtime_state_change',
            'c142_operator_go_no_go_review_only', 'c142_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'backup_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_go_decision_finalization_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c141_hash', 'actual_c141_hash', 'c141_hash_match',
            'expected_c141_file_sha1', 'actual_c141_file_sha1', 'c141_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_go_decision_finalization_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C142_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_OPERATOR_GO_NO_GO_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C142 weekly swing watchlist production/live runtime activation operator go/no-go review completed');
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
