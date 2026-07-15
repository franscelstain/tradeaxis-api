<?php

namespace App\Console\Commands\Watchlist;

use App\Application\Watchlist\Services\WatchlistBacktestC136WeeklySwingWatchlistProductionLiveRuntimeActivationGoDecisionFinalizationReviewService;
use Illuminate\Console\Command;

class RunBacktestC136WeeklySwingWatchlistProductionLiveRuntimeActivationGoDecisionFinalizationReviewCommand extends Command
{
    protected $signature = 'watchlist:backtest-c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review
        {--c135-artifact=storage/app/watchlist/backtest/c135-weekly-swing-watchlist-production-live-runtime-activation-operator-go-no-go-review.json}
        {--expected-c135-hash=a1573ce8ba1543ce8a98c08c17eefe519e4ca710}
        {--expected-c135-file-sha1=B283F81F0F10AD0CB46BE3C1BFF2A4ABFA27B1A2}
        {--approval-reference=}
        {--output=storage/app/watchlist/backtest/c136-weekly-swing-watchlist-production-live-runtime-activation-go-decision-finalization-review.json}
        {--operator-approved}
        {--go-decision-finalization-confirmed}
        {--overwrite}
        {--progress}';

    protected $description = 'Run C136 weekly swing watchlist production/live runtime activation GO decision finalization review without activating runtime bridge, live output, or PLAN/CONFIRM mutation.';

    private WatchlistBacktestC136WeeklySwingWatchlistProductionLiveRuntimeActivationGoDecisionFinalizationReviewService $service;

    public function __construct(?WatchlistBacktestC136WeeklySwingWatchlistProductionLiveRuntimeActivationGoDecisionFinalizationReviewService $service = null)
    {
        parent::__construct();
        $this->service = $service ?: new WatchlistBacktestC136WeeklySwingWatchlistProductionLiveRuntimeActivationGoDecisionFinalizationReviewService();
    }

    public function handle(): int
    {
        if ((bool) $this->option('progress')) {
            $this->line('C136 weekly swing watchlist production/live runtime activation GO decision finalization review started');
        }

        $result = $this->service->execute(
            (string) $this->option('c135-artifact'),
            (string) $this->option('expected-c135-hash'),
            (string) $this->option('expected-c135-file-sha1'),
            (string) $this->option('output'),
            [
                'overwrite' => (bool) $this->option('overwrite'),
                'operator_approved' => (bool) $this->option('operator-approved'),
                'go_decision_finalization_confirmed' => (bool) $this->option('go-decision-finalization-confirmed'),
                'approval_reference' => (string) $this->option('approval-reference'),
            ]
        );

        foreach ([
            'run_code', 'phase_label', 'status', 'reason_code', 'artifact_path', 'artifact_hash',
            'weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_review_executed',
            'weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_review_allowed',
            'weekly_swing_watchlist_production_live_runtime_activation_go_decision_finalization_review_pass',
            'production_live_runtime_activation_go_decision_finalization_review_pass',
            'operator_go_decision', 'operator_go_decision_confirmed', 'go_decision_finalized', 'go_decision_finalization_confirmed',
            'weekly_swing_watchlist_ready_for_production_live_runtime_activation_pre_activation_boundary_review',
            'ready_for_production_live_runtime_activation_pre_activation_boundary_review',
            'production_live_runtime_activation_go_decision_finalization_manifest_created',
            'production_live_runtime_activation_pre_activation_boundary_review_allowed_next',
            'production_live_runtime_activation_executed',
            'c135_lock_valid', 'c135_activation_operator_go_no_go_valid', 'c135_convert_from_json_pass',
            'c134_activation_observation_result_review_valid', 'c133_activation_observation_review_valid',
            'c132_activation_execution_review_valid', 'c131_activation_approval_valid',
            'c130_activation_readiness_valid', 'c129_final_closure_valid',
            'c135_operator_go_no_go_review_only', 'c135_not_live_runtime_state_change',
            'c136_go_decision_finalization_review_only', 'c136_not_live_runtime_state_change',
            'primary_candidate_ready_for_production_live_runtime_activation_pre_activation_boundary_review',
            'backup_candidate_ready_for_production_live_runtime_activation_pre_activation_boundary_review',
            'comparator_candidate_ready_for_production_live_runtime_activation_pre_activation_boundary_review',
            'a01_remains_comparator_only',
            'temporary_negative_artifacts_remaining', 'temporary_negative_artifact_cleanup_confirmed', 'temporary_negative_artifact_paths',
            'production_ready', 'production_catalog_runtime_wired', 'production_runtime_wiring_allowed', 'production_runtime_wiring_executed',
            'production_deployment_allowed', 'production_deployment_executed', 'runtime_bridge_active', 'controlled_rollout_active',
            'plan_confirm_mutation_allowed', 'plan_confirm_mutated',
            'weekly_swing_watchlist_runtime_active', 'weekly_swing_watchlist_live_output_enabled',
            'weekly_swing_watchlist_official_output_generated', 'weekly_swing_watchlist_official_output_published',
            'weekly_swing_watchlist_live_recommendation_generated',
            'expected_c135_hash', 'actual_c135_hash', 'c135_hash_match',
            'expected_c135_file_sha1', 'actual_c135_file_sha1', 'c135_file_sha1_match',
            'diagnostic_conclusion', 'next_step_recommendation'
        ] as $key) {
            if (array_key_exists($key, $result)) {
                $this->line($key.'='.$this->scalar($result[$key]));
            }
        }

        foreach ((array) ($result['next_pre_activation_boundary_decision'] ?? []) as $key => $value) {
            if (! is_array($value)) {
                $this->line('next_'.$key.'='.$this->scalar($value));
            }
        }

        if (strpos((string) ($result['status'] ?? ''), 'C136_WEEKLY_SWING_WATCHLIST_PRODUCTION_LIVE_RUNTIME_ACTIVATION_GO_DECISION_FINALIZATION_REVIEW_PASSED') === 0) {
            if ((bool) $this->option('progress')) {
                $this->line('C136 weekly swing watchlist production/live runtime activation GO decision finalization review completed');
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
